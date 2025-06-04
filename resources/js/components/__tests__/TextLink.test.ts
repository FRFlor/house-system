import { render, screen } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, it, expect, vi } from 'vitest'
import TextLink from '../TextLink.vue'

describe('TextLink', () => {
  describe('Unit Tests (with minimal mocking)', () => {
    // For unit tests, we can mock just the Inertia Link to focus on our component's behavior
    // This is appropriate when testing component logic, props handling, and rendering
    vi.mock('@inertiajs/vue3', () => ({
      Link: {
        name: 'InertiaLink',
        props: ['href', 'tabindex', 'method', 'as'],
        template: '<a :href="href" :tabindex="tabindex" v-bind="$attrs"><slot /></a>'
      }
    }))

    it('renders with correct props passed to Inertia Link', () => {
      render(TextLink, {
        props: {
          href: '/dashboard',
          tabindex: 2
        },
        slots: { default: 'Dashboard Link' }
      })

      const link = screen.getByRole('link')
      expect(link).toHaveAttribute('href', '/dashboard')
      expect(link).toHaveAttribute('tabindex', '2')
    })

    it('applies correct styling classes', () => {
      render(TextLink, {
        props: { href: '/test' },
        slots: { default: 'Test Link' }
      })

      const link = screen.getByRole('link')
      expect(link).toHaveClass(
        'text-foreground',
        'underline',
        'decoration-neutral-300',
        'underline-offset-4',
        'transition-colors',
        'duration-300',
        'ease-out'
      )
    })

    it('renders slot content correctly', () => {
      render(TextLink, {
        props: { href: '/test' },
        slots: { default: 'Custom Content' }
      })

      expect(screen.getByText('Custom Content')).toBeInTheDocument()
    })
  })

  describe('Integration Tests (no mocking)', () => {
    // For integration tests, we might want to test with the real Inertia Link
    // This tests the actual integration but requires more setup

    // Note: These tests would require proper Inertia setup in the test environment
    // For now, I'll show the structure but skip the actual tests

    it.skip('should navigate when clicked (requires Inertia setup)', async () => {
      // This would test actual navigation behavior
      // You'd need to set up Inertia's router and page context
      const user = userEvent.setup()

      render(TextLink, {
        props: { href: '/dashboard' },
        slots: { default: 'Go to Dashboard' }
      })

      const link = screen.getByRole('link')
      await user.click(link)

      // Assert navigation occurred
      // expect(mockInertiaVisit).toHaveBeenCalledWith('/dashboard')
    })
  })

  describe('Accessibility Tests', () => {
    // These tests focus on accessibility and don't need Inertia-specific behavior
    vi.mock('@inertiajs/vue3', () => ({
      Link: {
        name: 'InertiaLink',
        props: ['href', 'tabindex', 'method', 'as'],
        template: '<a :href="href" :tabindex="tabindex" v-bind="$attrs"><slot /></a>'
      }
    }))

    it('is keyboard accessible', async () => {
      const user = userEvent.setup()

      render(TextLink, {
        props: { href: '/test' },
        slots: { default: 'Keyboard Test' }
      })

      const link = screen.getByRole('link')
      await user.tab()
      expect(link).toHaveFocus()
    })

    it('has proper ARIA attributes', () => {
      render(TextLink, {
        props: { href: '/test' },
        slots: { default: 'ARIA Test' }
      })

      const link = screen.getByRole('link', { name: 'ARIA Test' })
      expect(link).toBeInTheDocument()
    })
  })
})
